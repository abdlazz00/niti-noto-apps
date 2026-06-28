<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import ToggleSwitch from 'primevue/toggleswitch';

defineOptions({ layout: AppLayout });

const props = defineProps({ table: Object });

const form = useForm({
    name:      props.table.name,
    number:    props.table.number,
    is_active: props.table.is_active,
    _method:   'PUT',
});

function submit() {
    form.post(route('owner.tables.update', props.table.id));
}
</script>

<template>
    <Head title="Edit Meja" />

    <div class="max-w-lg space-y-6">
        <div class="flex items-center gap-3">
            <Link :href="route('owner.tables.index')">
                <Button icon="pi pi-arrow-left" text rounded severity="secondary" />
            </Link>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Edit Meja</h1>
                <p class="text-slate-500 text-sm">Perbarui data Meja {{ table.number }}</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Nomor Meja <span class="text-red-500">*</span>
                </label>
                <InputNumber
                    v-model="form.number"
                    class="w-full"
                    :class="{ 'p-invalid': form.errors.number }"
                    :min="1"
                    pt:root:class="w-full"
                    pt:pcinputtext:root:class="w-full"
                />
                <small v-if="form.errors.number" class="text-red-500">{{ form.errors.number }}</small>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Nama Meja <span class="text-red-500">*</span>
                </label>
                <InputText
                    v-model="form.name"
                    class="w-full"
                    :class="{ 'p-invalid': form.errors.name }"
                />
                <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status</label>
                <div class="flex items-center gap-3">
                    <ToggleSwitch v-model="form.is_active" />
                    <span class="text-sm" :class="form.is_active ? 'text-emerald-600' : 'text-slate-400'">
                        {{ form.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <Link :href="route('owner.tables.index')">
                    <Button type="button" label="Batal" icon="pi pi-times" outlined severity="secondary" />
                </Link>
                <Button type="submit" label="Simpan Perubahan" icon="pi pi-check" severity="warn" :loading="form.processing" />
            </div>
        </form>
    </div>
</template>
