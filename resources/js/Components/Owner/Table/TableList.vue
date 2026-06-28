<script setup>
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import QRCanvas from '@/Components/Owner/Table/QRCanvas.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ tables: Array });

const emit = defineEmits(['toggle', 'delete']);
</script>

<template>
    <div v-if="tables.length === 0" class="text-center py-12 bg-white rounded-2xl border border-slate-100 shadow-sm text-slate-400">
        <i class="pi pi-table text-4xl mb-3 block text-slate-300" />
        <p class="font-medium text-sm">Belum ada meja terdaftar</p>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div
            v-for="table in tables"
            :key="table.id"
            class="bg-white rounded-2xl border border-slate-150 shadow-sm hover:shadow-md hover:border-amber-300 transition-all p-5 flex flex-col items-center text-center relative overflow-hidden"
        >
            <!-- QR code container (Click to see large or print) -->
            <div class="mb-4 p-3 bg-slate-50 rounded-xl border border-slate-100/50 flex items-center justify-center relative group">
                <QRCanvas :url="table.qr_url" :size="120" />
                <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity rounded-xl flex items-center justify-center">
                    <Link :href="route('owner.tables.qr', table.id)" target="_blank">
                        <Button icon="pi pi-print" severity="warn" size="small" rounded v-tooltip.top="'Cetak QR Meja'" />
                    </Link>
                </div>
            </div>

            <!-- Table Info -->
            <div class="space-y-1.5 mb-5 w-full">
                <div class="flex items-center justify-center gap-2">
                    <span class="text-xs font-bold px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-100 rounded-full">
                        Meja {{ table.number }}
                    </span>
                    <Tag
                        :value="table.is_active ? 'Aktif' : 'Nonaktif'"
                        :severity="table.is_active ? 'success' : 'secondary'"
                        class="text-[10px] uppercase font-bold"
                    />
                </div>
                <h3 class="text-base font-bold text-slate-800 truncate px-2" :title="table.name">
                    {{ table.name }}
                </h3>
            </div>

            <!-- Action Buttons Grid -->
            <div class="mt-auto w-full pt-4 border-t border-slate-100/70 flex items-center justify-between gap-2">
                <div class="flex gap-1.5">
                    <Link :href="route('owner.tables.edit', table.id)">
                        <Button icon="pi pi-pencil" size="small" outlined severity="info" class="p-2 rounded-lg" v-tooltip.top="'Edit'" />
                    </Link>
                    <Button
                        icon="pi pi-trash"
                        size="small"
                        outlined
                        severity="danger"
                        class="p-2 rounded-lg"
                        v-tooltip.top="'Hapus'"
                        @click="emit('delete', table)"
                    />
                </div>
                
                <Button
                    :icon="table.is_active ? 'pi pi-eye-slash' : 'pi pi-eye'"
                    :label="table.is_active ? 'Nonaktif' : 'Aktifkan'"
                    size="small"
                    :severity="table.is_active ? 'warn' : 'success'"
                    class="text-xs font-bold py-1.5 px-3 rounded-lg"
                    @click="emit('toggle', table)"
                />
            </div>
        </div>
    </div>
</template>
