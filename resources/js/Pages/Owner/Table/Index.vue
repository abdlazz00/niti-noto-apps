<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import TableList from '@/Components/Owner/Table/TableList.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import Button from 'primevue/button';
import ConfirmDialog from 'primevue/confirmdialog';
import Toast from 'primevue/toast';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';

defineOptions({ layout: AppLayout });

const props = defineProps({ tables: Array });

const confirm = useConfirm();
const toast = useToast();
const page = usePage();

watch(() => page.props.flash, (flash) => {
    if (flash?.success) toast.add({ severity: 'success', summary: 'Berhasil', detail: flash.success, life: 3000 });
    if (flash?.error) toast.add({ severity: 'error', summary: 'Gagal', detail: flash.error, life: 4000 });
}, { deep: true });

function onToggle(table) {
    const action = table.is_active ? 'nonaktifkan' : 'aktifkan';
    confirm.require({
        message: `Yakin ingin ${action} Meja ${table.number} (${table.name})?`,
        header: 'Konfirmasi',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        accept: () => router.patch(route('owner.tables.toggle-active', table.id), {}, {
            onSuccess: () => toast.add({ severity: 'success', summary: 'Berhasil', detail: `Meja berhasil di${action}kan.`, life: 3000 }),
        }),
    });
}

function onDelete(table) {
    confirm.require({
        message: `Hapus Meja ${table.number} (${table.name})? Aksi ini tidak bisa dibatalkan.`,
        header: 'Hapus Meja',
        icon: 'pi pi-trash',
        acceptLabel: 'Hapus',
        acceptClass: 'p-button-danger',
        rejectLabel: 'Batal',
        accept: () => router.delete(route('owner.tables.destroy', table.id), {
            onSuccess: () => toast.add({ severity: 'info', summary: 'Dihapus', detail: 'Meja berhasil dihapus.', life: 3000 }),
        }),
    });
}
</script>

<template>
    <Head title="Manajemen Meja" />
    <Toast />
    <ConfirmDialog />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Manajemen Meja</h1>
                <p class="text-slate-500 text-sm mt-1">Kelola meja dan QR code warung</p>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="route('owner.tables.print-all')">
                    <Button label="Print Semua QR" icon="pi pi-print" outlined severity="secondary" />
                </Link>
                <Link :href="route('owner.tables.create')">
                    <Button label="Tambah Meja" icon="pi pi-plus" severity="warn" />
                </Link>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <TableList :tables="tables" @toggle="onToggle" @delete="onDelete" />
        </div>
    </div>
</template>
