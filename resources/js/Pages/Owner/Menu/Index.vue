<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import MenuItemTable from '@/Components/Owner/Menu/MenuItemTable.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import ConfirmDialog from 'primevue/confirmdialog';
import Toast from 'primevue/toast';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';

defineOptions({ layout: AppLayout });

const props = defineProps({ items: Array, categories: Array });

const confirm = useConfirm();
const toast = useToast();
const page = usePage();

const search = ref('');
const selectedCategory = ref(null);

const categoryOptions = computed(() => [
    { label: 'Semua Kategori', value: null },
    ...props.categories.map(c => ({ label: c.name, value: c.id })),
]);

const filtered = computed(() =>
    props.items.filter(item => {
        const matchSearch = !search.value ||
            item.name.toLowerCase().includes(search.value.toLowerCase());
        const matchCategory = !selectedCategory.value ||
            item.category_id === selectedCategory.value;
        return matchSearch && matchCategory;
    })
);

watch(() => page.props.flash, (flash) => {
    if (flash?.success) toast.add({ severity: 'success', summary: 'Berhasil', detail: flash.success, life: 3000 });
    if (flash?.error) toast.add({ severity: 'error', summary: 'Gagal', detail: flash.error, life: 4000 });
}, { deep: true });

function onToggle(item) {
    const action = item.is_active ? 'nonaktifkan' : 'aktifkan';
    confirm.require({
        message: `Yakin ingin ${action} "${item.name}"?`,
        header: 'Konfirmasi',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        accept: () => router.patch(route('owner.menu-items.toggle-active', item.id), {}, {
            onSuccess: () => toast.add({ severity: 'success', summary: 'Berhasil', detail: `Menu berhasil di${action}kan.`, life: 3000 }),
        }),
    });
}

function onDelete(item) {
    confirm.require({
        message: `Hapus "${item.name}"? Aksi ini tidak bisa dibatalkan.`,
        header: 'Hapus Menu Item',
        icon: 'pi pi-trash',
        acceptLabel: 'Hapus',
        acceptClass: 'p-button-danger',
        rejectLabel: 'Batal',
        accept: () => router.delete(route('owner.menu-items.destroy', item.id), {
            onSuccess: () => toast.add({ severity: 'info', summary: 'Dihapus', detail: `"${item.name}" telah dihapus.`, life: 3000 }),
        }),
    });
}
</script>

<template>
    <Head title="Menu Items" />
    <Toast />
    <ConfirmDialog />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Manajemen Menu</h1>
                <p class="text-slate-500 text-sm mt-1">Kelola daftar menu warung</p>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="route('owner.categories.index')">
                    <Button label="Kategori" icon="pi pi-tags" outlined severity="secondary" />
                </Link>
                <Link :href="route('owner.menu-items.create')">
                    <Button label="Tambah Menu" icon="pi pi-plus" severity="warn" />
                </Link>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-48">
                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm" />
                    <InputText v-model="search" placeholder="Cari nama menu..." class="w-full pl-9" />
                </div>
                <Select
                    v-model="selectedCategory"
                    :options="categoryOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Semua Kategori"
                    class="w-48"
                />
                <span class="text-sm text-slate-400">{{ filtered.length }} menu</span>
            </div>

            <MenuItemTable :items="filtered" @toggle="onToggle" @delete="onDelete" />
        </div>
    </div>
</template>
