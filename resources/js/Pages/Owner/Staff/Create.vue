<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import StaffForm from '@/Components/Owner/Staff/StaffForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';

defineOptions({ layout: AppLayout });

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: null,
    photo: null,
    phone: '',
    address: '',
    join_date: null,
    notes: '',
});

function submit() {
    form.post(route('owner.staff.store'), { forceFormData: true });
}
</script>

<template>
    <Head title="Tambah Staff" />

    <div class="max-w-2xl space-y-6">
        <div class="flex items-center gap-3">
            <Link :href="route('owner.staff.index')">
                <Button icon="pi pi-arrow-left" text rounded severity="secondary" />
            </Link>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Tambah Staff</h1>
                <p class="text-slate-500 text-sm">Buat akun untuk user internal baru</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <StaffForm :form="form" :is-edit="false" />

            <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
                <Link :href="route('owner.staff.index')">
                    <Button type="button" label="Batal" outlined severity="secondary" />
                </Link>
                <Button type="submit" label="Simpan Staff" icon="pi pi-check" severity="warn" :loading="form.processing" />
            </div>
        </form>
    </div>
</template>
