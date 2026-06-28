<script setup>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import QRCanvas from '@/Components/Owner/Table/QRCanvas.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ tables: Array });

const emit = defineEmits(['toggle', 'delete']);
</script>

<template>
    <DataTable
        :value="tables"
        :rows="15"
        paginator
        :rowsPerPageOptions="[10, 15, 25]"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
        stripedRows
        class="text-sm"
    >
        <!-- Nomor -->
        <Column header="No." field="number" sortable style="width: 70px">
            <template #body="{ data }">
                <span class="font-bold text-amber-700 text-base">{{ data.number }}</span>
            </template>
        </Column>

        <!-- Nama -->
        <Column header="Nama Meja" field="name" sortable>
            <template #body="{ data }">
                <span class="font-medium text-slate-800">{{ data.name }}</span>
            </template>
        </Column>

        <!-- QR Preview -->
        <Column header="QR Code" style="width: 90px">
            <template #body="{ data }">
                <QRCanvas :url="data.qr_url" :size="60" />
            </template>
        </Column>

        <!-- Status -->
        <Column header="Status" style="width: 100px">
            <template #body="{ data }">
                <Tag
                    :value="data.is_active ? 'Aktif' : 'Nonaktif'"
                    :severity="data.is_active ? 'success' : 'secondary'"
                />
            </template>
        </Column>

        <!-- Aksi -->
        <Column header="Aksi" style="width: 160px">
            <template #body="{ data }">
                <div class="flex items-center gap-1">
                    <Link :href="route('owner.tables.qr', data.id)">
                        <Button icon="pi pi-qrcode" size="small" text rounded severity="secondary" v-tooltip="'Print QR'" />
                    </Link>
                    <Link :href="route('owner.tables.edit', data.id)">
                        <Button icon="pi pi-pencil" size="small" text rounded severity="info" v-tooltip="'Edit'" />
                    </Link>
                    <Button
                        :icon="data.is_active ? 'pi pi-eye-slash' : 'pi pi-eye'"
                        size="small" text rounded
                        :severity="data.is_active ? 'warn' : 'success'"
                        v-tooltip="data.is_active ? 'Nonaktifkan' : 'Aktifkan'"
                        @click="emit('toggle', data)"
                    />
                    <Button
                        icon="pi pi-trash"
                        size="small" text rounded severity="danger"
                        v-tooltip="'Hapus'"
                        @click="emit('delete', data)"
                    />
                </div>
            </template>
        </Column>

        <template #empty>
            <div class="text-center py-10 text-slate-400">
                <i class="pi pi-table text-3xl mb-2 block" />
                <p>Belum ada meja terdaftar</p>
            </div>
        </template>
    </DataTable>
</template>
