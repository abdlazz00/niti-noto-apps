<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import StaffTable from '@/Components/Owner/Staff/StaffTable.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';

defineOptions({ layout: AppLayout });

const props = defineProps({ staff: Array });

const confirm = useConfirm();
const toast = useToast();
const search = ref('');

const filtered = computed(() =>
    props.staff.filter(s =>
        s.name.toLowerCase().includes(search.value.toLowerCase()) ||
        s.email.toLowerCase().includes(search.value.toLowerCase())
    )
);

function onToggle(staff) {
    const action = staff.is_active ? 'nonaktifkan' : 'aktifkan';
    confirm.require({
        message: `Yakin ingin ${action} ${staff.name}?`,
        header: 'Konfirmasi',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        accept: () => router.patch(route('owner.staff.toggle-active', staff.id), {}, {
            onSuccess: () => toast.add({ severity: 'success', summary: 'Berhasil', detail: `Staff berhasil di${action}kan.`, life: 3000 }),
        }),
    });
}

function onDelete(staff) {
    confirm.require({
        message: `Nonaktifkan ${staff.name}? Data tidak akan dihapus.`,
        header: 'Nonaktifkan Staff',
        icon: 'pi pi-user-minus',
        acceptLabel: 'Nonaktifkan',
        acceptClass: 'p-button-danger',
        rejectLabel: 'Batal',
        accept: () => router.delete(route('owner.staff.destroy', staff.id), {
            onSuccess: () => toast.add({ severity: 'info', summary: 'Dinonaktifkan', detail: `${staff.name} telah dinonaktifkan.`, life: 3000 }),
        }),
    });
}
</script>

<template>
    <Head title="Manajemen Staff" />
    <ConfirmDialog />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Manajemen Staff</h1>
                <p class="text-slate-500 text-sm mt-1">Kelola user internal warung</p>
            </div>
            <Link :href="route('owner.staff.create')">
                <Button label="Tambah Staff" icon="pi pi-plus" severity="warn" />
            </Link>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <div class="relative max-w-xs">
                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm" />
                    <InputText v-model="search" placeholder="Cari nama atau email..." class="w-full pl-9" />
                </div>
            </div>

            <StaffTable :staff="filtered" @toggle="onToggle" @delete="onDelete" />
        </div>
    </div>
</template>
