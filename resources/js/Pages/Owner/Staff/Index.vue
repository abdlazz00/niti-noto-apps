<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';
import ConfirmDialog from 'primevue/confirmdialog';

defineOptions({ layout: AppLayout });

const props = defineProps({
    staff: Array,
});

const confirm = useConfirm();
const toast = useToast();
const search = ref('');

const filtered = computed(() =>
    props.staff.filter(s =>
        s.name.toLowerCase().includes(search.value.toLowerCase()) ||
        s.email.toLowerCase().includes(search.value.toLowerCase())
    )
);

const roleColor = (role) => ({
    owner: 'danger',
    cashier: 'warn',
    staff: 'info',
}[role] ?? 'secondary');

const roleLabel = (role) => ({
    owner: 'Owner',
    cashier: 'Kasir',
    staff: 'Staff',
}[role] ?? role);

function toggleActive(staff) {
    const action = staff.is_active ? 'nonaktifkan' : 'aktifkan';
    confirm.require({
        message: `Yakin ingin ${action} ${staff.name}?`,
        header: 'Konfirmasi',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        accept: () => {
            router.patch(route('owner.staff.toggle-active', staff.id), {}, {
                onSuccess: () => toast.add({ severity: 'success', summary: 'Berhasil', detail: `Staff berhasil di${action}kan.`, life: 3000 }),
            });
        },
    });
}

function confirmDelete(staff) {
    confirm.require({
        message: `Nonaktifkan ${staff.name}? Data tidak akan dihapus.`,
        header: 'Nonaktifkan Staff',
        icon: 'pi pi-user-minus',
        acceptLabel: 'Nonaktifkan',
        acceptClass: 'p-button-danger',
        rejectLabel: 'Batal',
        accept: () => {
            router.delete(route('owner.staff.destroy', staff.id), {
                onSuccess: () => toast.add({ severity: 'info', summary: 'Dinonaktifkan', detail: `${staff.name} telah dinonaktifkan.`, life: 3000 }),
            });
        },
    });
}
</script>

<template>
    <Head title="Manajemen Staff" />
    <ConfirmDialog />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Manajemen Staff</h1>
                <p class="text-slate-500 text-sm mt-1">Kelola user internal warung</p>
            </div>
            <Link :href="route('owner.staff.create')">
                <Button label="Tambah Staff" icon="pi pi-plus" severity="warn" />
            </Link>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <!-- Search bar -->
            <div class="p-4 border-b border-slate-100">
                <div class="relative max-w-xs">
                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm" />
                    <InputText
                        v-model="search"
                        placeholder="Cari nama atau email..."
                        class="w-full pl-9"
                    />
                </div>
            </div>

            <DataTable
                :value="filtered"
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
                            <img
                                v-if="data.photo"
                                :src="`/storage/${data.photo}`"
                                :alt="data.name"
                                class="w-full h-full object-cover"
                            />
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
                            {{ data.join_date ? new Date(data.join_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-' }}
                        </span>
                    </template>
                </Column>

                <!-- Status -->
                <Column header="Status" style="width: 90px">
                    <template #body="{ data }">
                        <Tag
                            :value="data.is_active ? 'Aktif' : 'Nonaktif'"
                            :severity="data.is_active ? 'success' : 'secondary'"
                        />
                    </template>
                </Column>

                <!-- Aksi -->
                <Column header="Aksi" style="width: 140px">
                    <template #body="{ data }">
                        <div class="flex items-center gap-1">
                            <Link :href="route('owner.staff.edit', data.id)">
                                <Button icon="pi pi-pencil" size="small" text rounded severity="info" v-tooltip="'Edit'" />
                            </Link>
                            <Button
                                :icon="data.is_active ? 'pi pi-eye-slash' : 'pi pi-eye'"
                                size="small" text rounded
                                :severity="data.is_active ? 'warn' : 'success'"
                                v-tooltip="data.is_active ? 'Nonaktifkan' : 'Aktifkan'"
                                @click="toggleActive(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                size="small" text rounded severity="danger"
                                v-tooltip="'Nonaktifkan'"
                                @click="confirmDelete(data)"
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
        </div>
    </div>
</template>
