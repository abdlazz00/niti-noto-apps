<script setup>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import { Link } from '@inertiajs/vue3';

defineProps({ items: Array });

const emit = defineEmits(['toggle', 'delete']);

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
}
</script>

<template>
    <DataTable
        :value="items"
        :rows="15"
        paginator
        :rowsPerPageOptions="[10, 15, 25]"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
        stripedRows
        class="text-sm"
    >
        <!-- Gambar -->
        <Column header="Foto" style="width: 72px">
            <template #body="{ data }">
                <div class="w-12 h-12 rounded-lg overflow-hidden bg-slate-100 flex items-center justify-center">
                    <img v-if="data.image" :src="`/storage/${data.image}`" :alt="data.name" class="w-full h-full object-cover" />
                    <i v-else class="pi pi-image text-slate-300 text-lg" />
                </div>
            </template>
        </Column>

        <!-- Nama & Kategori -->
        <Column header="Menu" field="name" sortable>
            <template #body="{ data }">
                <div>
                    <p class="font-medium text-slate-800">{{ data.name }}</p>
                    <p class="text-xs text-slate-400">{{ data.category_name }}</p>
                </div>
            </template>
        </Column>

        <!-- Harga -->
        <Column header="Harga" field="price" sortable style="width: 150px">
            <template #body="{ data }">
                <span class="font-semibold text-amber-700">{{ formatPrice(data.price) }}</span>
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
        <Column header="Aksi" style="width: 130px">
            <template #body="{ data }">
                <div class="flex items-center gap-1">
                    <Link :href="route('owner.menu-items.edit', data.id)">
                        <Button icon="pi pi-pencil" size="small" text rounded severity="info" v-tooltip="'Edit'" />
                    </Link>
                    <Button
                        :icon="data.is_active ? 'pi pi-eye-slash' : 'pi pi-eye'"
                        size="small" text rounded
                        :severity="data.is_active ? 'warn' : 'success'"
                        :v-tooltip="data.is_active ? 'Nonaktifkan' : 'Aktifkan'"
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
                <i class="pi pi-list text-3xl mb-2 block" />
                <p>Belum ada menu ditemukan</p>
            </div>
        </template>
    </DataTable>
</template>
