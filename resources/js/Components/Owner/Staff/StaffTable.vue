<script setup>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import { Link } from '@inertiajs/vue3';

defineProps({
    staff: Array,
    search: String,
});

const emit = defineEmits(['toggle', 'delete']);

const roleColor = (role) => ({ owner: 'danger', cashier: 'warn', staff: 'info' }[role] ?? 'secondary');
const roleLabel = (role) => ({ owner: 'Owner', cashier: 'Kasir', staff: 'Staff' }[role] ?? role);
</script>

<template>
    <DataTable
        :value="staff"
        :rows="15"
        paginator
        :rowsPerPageOptions="[10, 15, 25]"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
        stripedRows
        class="text-sm"
    >
        <!-- Foto -->
        <Column header="Foto" style="width: 64px">
            <template #body="{ data }">
                <div class="w-10 h-10 rounded-full overflow-hidden bg-amber-100 flex items-center justify-center">
                    <img v-if="data.photo" :src="`/storage/${data.photo}`" :alt="data.name" class="w-full h-full object-cover" />
                    <span v-else class="text-amber-700 font-semibold text-sm">
                        {{ data.name.charAt(0).toUpperCase() }}
                    </span>
                </div>
            </template>
        </Column>

        <!-- Nama & Email -->
        <Column header="Staff" sortable field="name">
            <template #body="{ data }">
                <div>
                    <p class="font-medium text-slate-800">{{ data.name }}</p>
                    <p class="text-xs text-slate-500">{{ data.email }}</p>
                </div>
            </template>
        </Column>

        <!-- Role -->
        <Column header="Role" style="width: 100px">
            <template #body="{ data }">
                <Tag :value="roleLabel(data.role)" :severity="roleColor(data.role)" />
            </template>
        </Column>

        <!-- Phone -->
        <Column header="No. HP" field="phone" style="width: 140px">
            <template #body="{ data }">
                <span class="text-slate-600">{{ data.phone ?? '-' }}</span>
            </template>
        </Column>

        <!-- Join Date -->
        <Column header="Bergabung" style="width: 120px">
            <template #body="{ data }">
                <span class="text-slate-600 text-xs">
                    {{ data.join_date
                        ? new Date(data.join_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
                        : '-' }}
                </span>
            </template>
        </Column>

        <!-- Status -->
        <Column header="Status" style="width: 90px">
            <template #body="{ data }">
                <Tag :value="data.is_active ? 'Aktif' : 'Nonaktif'" :severity="data.is_active ? 'success' : 'secondary'" />
            </template>
        </Column>

        <!-- Aksi -->
        <Column header="Aksi" style="width: 130px">
            <template #body="{ data }">
                <div class="flex items-center gap-1">
                    <Link :href="route('owner.staff.edit', data.id)">
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
                        v-tooltip="'Nonaktifkan'"
                        @click="emit('delete', data)"
                    />
                </div>
            </template>
        </Column>

        <template #empty>
            <div class="text-center py-10 text-slate-400">
                <i class="pi pi-users text-3xl mb-2 block" />
                <p>Belum ada staff ditemukan</p>
            </div>
        </template>
    </DataTable>
</template>
