<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import MenuItemForm from '@/Components/Owner/Menu/MenuItemForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';

defineOptions({ layout: AppLayout });

const props = defineProps({ item: Object, categories: Array });

const form = useForm({
    name:        props.item.name,
    category_id: props.item.category_id,
    price:       parseFloat(props.item.price),
    is_active:   props.item.is_active,
    image:       null,
    _method:     'PUT',
});

function submit() {
    form.post(route('owner.menu-items.update', props.item.id), { forceFormData: true });
}
</script>

<template>
    <Head title="Edit Menu" />

    <div class="max-w-2xl space-y-6">
        <div class="flex items-center gap-3">
            <Link :href="route('owner.menu-items.index')">
                <Button icon="pi pi-arrow-left" text rounded severity="secondary" />
            </Link>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Edit Menu</h1>
                <p class="text-slate-500 text-sm">Perbarui data {{ item.name }}</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <MenuItemForm
                :form="form"
                :categories="categories"
                :is-edit="true"
                :initial-image-url="item.image ? `/storage/${item.image}` : null"
            />

            <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
                <Link :href="route('owner.menu-items.index')">
                    <Button type="button" label="Batal" outlined severity="secondary" />
                </Link>
                <Button type="submit" label="Simpan Perubahan" icon="pi pi-check" severity="warn" :loading="form.processing" />
            </div>
        </form>
    </div>
</template>
