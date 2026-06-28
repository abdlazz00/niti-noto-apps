<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { animate } from 'motion';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

defineOptions({ layout: AppLayout });

const props = defineProps({
    diterima:     Array,
    sedangDibuat: Array,
});

const toast = useToast();
const page  = usePage();

const diterimaList    = ref([...props.diterima]);
const sedangDibuatList = ref([...props.sedangDibuat]);

watch(() => page.props.flash, (flash) => {
    if (flash?.success) toast.add({ severity: 'success', summary: flash.success, life: 3000 });
    if (flash?.error)   toast.add({ severity: 'error',   summary: flash.error,   life: 4000 });
}, { immediate: true });

// Sync when Inertia reloads
watch(() => props.diterima,     val => { diterimaList.value = [...val]; });
watch(() => props.sedangDibuat, val => { sedangDibuatList.value = [...val]; });

// Motion.js — animate a card in
async function animateIn(id) {
    await nextTick();
    const el = document.querySelector(`[data-order-id="${id}"]`);
    if (el) {
        animate(el, { opacity: [0, 1], y: [-24, 0], scale: [0.95, 1] }, { duration: 0.35, easing: 'ease-out' });
    }
}

async function animateOut(el, done) {
    await animate(el, { opacity: [1, 0], scale: [1, 0.9], y: [0, 12] }, { duration: 0.25, easing: 'ease-in' }).finished;
    done();
}

// Realtime — kitchen private channel
let kitchenChannel = null;

onMounted(() => {
    if (! window.Echo) return;

    kitchenChannel = window.Echo.private('kitchen');
    kitchenChannel.listen('KitchenNewOrder', (e) => {
        const exists = diterimaList.value.find(o => o.id === e.id);
        if (! exists) {
            diterimaList.value.push({
                id:           e.id,
                order_number: e.order_number,
                table_number: e.table_number,
                table_name:   e.table_name,
                status:       'diterima',
                notes:        e.notes,
                created_at:   new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
                next_status:  'sedang_dibuat',
                items:        e.items,
            });
            animateIn(e.id);
        }
        toast.add({ severity: 'info', summary: `Order baru: ${e.order_number}`, detail: `Meja ${e.table_number} — ${e.table_name}`, life: 5000 });
    });
});

onUnmounted(() => {
    if (window.Echo) window.Echo.leave('kitchen');
});

function updateStatus(order, newStatus) {
    router.patch(route('staff.queue.update', order.id), { status: newStatus }, {
        preserveScroll: true,
        onSuccess: () => {
            if (newStatus === 'sedang_dibuat') {
                diterimaList.value = diterimaList.value.filter(o => o.id !== order.id);
                sedangDibuatList.value.push({ ...order, status: 'sedang_dibuat', next_status: 'siap_diambil' });
                animateIn(order.id);
            } else if (newStatus === 'siap_diambil') {
                sedangDibuatList.value = sedangDibuatList.value.filter(o => o.id !== order.id);
            }
        },
    });
}

function formatTime(time) {
    return time ?? '';
}
</script>

<template>
    <Head title="Antrian Dapur — Staff" />
    <Toast />

    <div class="space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Antrian Dapur</h1>
                <p class="text-sm text-slate-500 mt-0.5">Update status pesanan secara realtime</p>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full bg-green-400 animate-pulse" />
                <span class="text-xs text-slate-400">Live update aktif</span>
            </div>
        </div>

        <!-- 2-column queue -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            <!-- Column: Diterima -->
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-amber-400" />
                    <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">
                        Diterima
                        <span class="ml-1 text-amber-500">({{ diterimaList.length }})</span>
                    </h2>
                </div>

                <div v-if="!diterimaList.length" class="bg-slate-50 rounded-2xl border border-slate-100 p-8 text-center text-slate-400">
                    <i class="pi pi-inbox text-3xl mb-2 block" />
                    <p class="text-sm">Belum ada order</p>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="order in diterimaList"
                        :key="order.id"
                        :data-order-id="order.id"
                        class="bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden"
                    >
                        <!-- Header -->
                        <div class="bg-amber-50 px-4 py-3 flex items-center justify-between">
                            <div>
                                <p class="font-black text-amber-700 text-sm">{{ order.order_number }}</p>
                                <p class="text-xs text-amber-600">Meja {{ order.table_number }}{{ order.table_name ? ` — ${order.table_name}` : '' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-400">{{ formatTime(order.created_at) }}</p>
                                <Tag value="Diterima" severity="warn" class="text-xs mt-1" />
                            </div>
                        </div>

                        <!-- Items -->
                        <div class="px-4 py-3 space-y-1.5">
                            <div
                                v-for="(item, idx) in order.items"
                                :key="idx"
                                class="flex items-start gap-2 text-sm"
                            >
                                <span class="font-semibold text-amber-600 shrink-0">{{ item.qty }}×</span>
                                <div>
                                    <span class="text-slate-700">{{ item.name }}</span>
                                    <span v-if="item.notes" class="block text-xs text-slate-400 italic">{{ item.notes }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="order.notes" class="mx-4 mb-3 bg-amber-50 rounded-xl px-3 py-2">
                            <p class="text-xs text-amber-700"><span class="font-semibold">Catatan:</span> {{ order.notes }}</p>
                        </div>

                        <!-- Action -->
                        <div class="px-4 py-3 border-t border-slate-100">
                            <Button
                                label="Mulai Buat"
                                icon="pi pi-cog"
                                class="w-full"
                                size="small"
                                @click="updateStatus(order, 'sedang_dibuat')"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column: Sedang Dibuat -->
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-blue-400" />
                    <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">
                        Sedang Dibuat
                        <span class="ml-1 text-blue-500">({{ sedangDibuatList.length }})</span>
                    </h2>
                </div>

                <div v-if="!sedangDibuatList.length" class="bg-slate-50 rounded-2xl border border-slate-100 p-8 text-center text-slate-400">
                    <i class="pi pi-cog text-3xl mb-2 block animate-spin" style="animation-duration: 3s" />
                    <p class="text-sm">Tidak ada yang sedang dibuat</p>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="order in sedangDibuatList"
                        :key="order.id"
                        :data-order-id="order.id"
                        class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden"
                    >
                        <!-- Header -->
                        <div class="bg-blue-50 px-4 py-3 flex items-center justify-between">
                            <div>
                                <p class="font-black text-blue-700 text-sm">{{ order.order_number }}</p>
                                <p class="text-xs text-blue-600">Meja {{ order.table_number }}{{ order.table_name ? ` — ${order.table_name}` : '' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-400">{{ formatTime(order.created_at) }}</p>
                                <Tag value="Sedang Dibuat" severity="info" class="text-xs mt-1" />
                            </div>
                        </div>

                        <!-- Items -->
                        <div class="px-4 py-3 space-y-1.5">
                            <div
                                v-for="(item, idx) in order.items"
                                :key="idx"
                                class="flex items-start gap-2 text-sm"
                            >
                                <span class="font-semibold text-blue-600 shrink-0">{{ item.qty }}×</span>
                                <div>
                                    <span class="text-slate-700">{{ item.name }}</span>
                                    <span v-if="item.notes" class="block text-xs text-slate-400 italic">{{ item.notes }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="order.notes" class="mx-4 mb-3 bg-blue-50 rounded-xl px-3 py-2">
                            <p class="text-xs text-blue-700"><span class="font-semibold">Catatan:</span> {{ order.notes }}</p>
                        </div>

                        <!-- Action -->
                        <div class="px-4 py-3 border-t border-slate-100">
                            <Button
                                label="Siap Diambil"
                                icon="pi pi-bell"
                                class="w-full"
                                size="small"
                                severity="success"
                                @click="updateStatus(order, 'siap_diambil')"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
