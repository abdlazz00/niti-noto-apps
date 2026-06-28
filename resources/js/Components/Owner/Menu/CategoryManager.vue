<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';

const props = defineProps({ categories: Array });

const confirm = useConfirm();
const toast = useToast();

const dialogVisible = ref(false);
const editingCategory = ref(null);

const form = useForm({ name: '' });

function openAdd() {
    editingCategory.value = null;
    form.reset();
    dialogVisible.value = true;
}

function openEdit(category) {
    editingCategory.value = category;
    form.name = category.name;
    dialogVisible.value = true;
}

function closeDialog() {
    dialogVisible.value = false;
    form.reset();
    form.clearErrors();
}

function submit() {
    if (editingCategory.value) {
        form.put(route('owner.categories.update', editingCategory.value.id), {
            onSuccess: () => {
                closeDialog();
                toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Kategori diperbarui.', life: 3000 });
            },
        });
    } else {
        form.post(route('owner.categories.store'), {
            onSuccess: () => {
                closeDialog();
                toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Kategori ditambahkan.', life: 3000 });
            },
        });
    }
}

function deleteCategory(category) {
    confirm.require({
        message: `Hapus kategori "${category.name}"? Aksi ini tidak bisa dibatalkan.`,
        header: 'Hapus Kategori',
        icon: 'pi pi-trash',
        acceptLabel: 'Hapus',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => router.delete(route('owner.categories.destroy', category.id), {
            onSuccess: () => toast.add({ severity: 'info', summary: 'Dihapus', detail: `Kategori "${category.name}" dihapus.`, life: 3000 }),
            onError: () => toast.add({ severity: 'error', summary: 'Gagal', detail: 'Kategori masih memiliki menu item.', life: 4000 }),
        }),
    });
}
</script>

<template>
    <ConfirmDialog />

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b border-slate-100">
            <div>
                <h2 class="font-semibold text-slate-800">Kategori Menu</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ categories.length }} kategori terdaftar</p>
            </div>
            <Button label="Tambah Kategori" icon="pi pi-plus" size="small" severity="warn" @click="openAdd" />
        </div>

        <DataTable :value="categories" stripedRows class="text-sm">
            <Column header="Nama Kategori" field="name" sortable>
                <template #body="{ data }">
                    <span class="font-medium text-slate-800">{{ data.name }}</span>
                </template>
            </Column>

            <Column header="Jumlah Menu" style="width: 140px" class="text-center">
                <template #body="{ data }">
                    <span class="inline-flex items-center gap-1 text-slate-500">
                        <i class="pi pi-list text-xs" />
                        {{ data.menu_items_count }} item
                    </span>
                </template>
            </Column>

            <Column header="Aksi" style="width: 110px">
                <template #body="{ data }">
                    <div class="flex items-center gap-1">
                        <Button icon="pi pi-pencil" size="small" text rounded severity="info" v-tooltip="'Edit'" @click="openEdit(data)" />
                        <Button
                            icon="pi pi-trash"
                            size="small" text rounded severity="danger"
                            v-tooltip="'Hapus'"
                            :disabled="data.menu_items_count > 0"
                            @click="deleteCategory(data)"
                        />
                    </div>
                </template>
            </Column>

            <template #empty>
                <div class="text-center py-10 text-slate-400">
                    <i class="pi pi-tags text-3xl mb-2 block" />
                    <p>Belum ada kategori</p>
                </div>
            </template>
        </DataTable>
    </div>

    <Dialog
        v-model:visible="dialogVisible"
        :header="editingCategory ? 'Edit Kategori' : 'Tambah Kategori'"
        :style="{ width: '400px' }"
        modal
        @hide="closeDialog"
    >
        <form @submit.prevent="submit" class="space-y-4 pt-2">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Nama Kategori <span class="text-red-500">*</span>
                </label>
                <InputText
                    v-model="form.name"
                    class="w-full"
                    :class="{ 'p-invalid': form.errors.name }"
                    placeholder="Contoh: Kopi, Non-Kopi, Makanan..."
                    autofocus
                />
                <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <Button type="button" label="Batal" outlined severity="secondary" @click="closeDialog" />
                <Button type="submit" :label="editingCategory ? 'Simpan' : 'Tambah'" severity="warn" :loading="form.processing" />
            </div>
        </form>
    </Dialog>
</template>
