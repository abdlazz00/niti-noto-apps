<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import MenuItemForm from '@/Components/Owner/Menu/MenuItemForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';

defineOptions({ layout: AppLayout });

const props = defineProps({ categories: Array });

const form = useForm({
    name:        '',
    category_id: null,
    price:       null,
    is_active:   true,
    image:       null,
});

function submit() {
    form.post(route('owner.menu-items.store'), { forceFormData: true });
}
</script>

<template>
    <Head title="Tambah Menu" />

    <div class="max-w-2xl space-y-6">
        <div class="flex items-center gap-3">
            <Link :href="route('owner.menu-items.index')">
                <Button icon="pi pi-arrow-left" text rounded severity="secondary" />
            </Link>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Tambah Menu</h1>
                <p class="text-slate-500 text-sm">Buat item menu baru</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <MenuItemForm :form="form" :categories="categories" :is-edit="false" />

            <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
                <Link :href="route('owner.menu-items.index')">
                    <Button type="button" label="Batal" outlined severity="secondary" />
                </Link>
                <Button type="submit" label="Simpan Menu" icon="pi pi-check" severity="warn" :loading="form.processing" />
            </div>
        </form>
    </div>
</template>
